# /usr/bin/env python3
import gzip
import hashlib
import os
import queue
import shlex
import shutil
import subprocess
import threading
import time
import requests

# API conf
base_url = 'http://inlovewith.space/dev/web'
get_work_url = base_url + '/?get_job'
put_work_url = base_url + '/?put_job'
agents_url = base_url + '/?agents_api'
alive_url = base_url + '/?agents_api'
user_key = ''
user_key_file = 'user_key.txt'

# Hashcat conf
hashcat = 'hashcat64.exe'
performance = '-w 3'
outfile = 'pass.key'
benchmark = 'benchmark.txt'
speed_regex = r"[0-9]+ [HKM]+(/s)"

# Folders
dict_folder = 'dicts/'
hccapx_folder = 'hccapx/'
hash_folder = 'hashes/'

# Cracker arguments
params = {
    '0': '{0} -m 2500 --potfile-disable --outfile-format=2 {1} -o {2} {3} {4}',  # WPA2PSK
    '1': '{0} -m 5500 --potfile-disable --outfile-format=2 {1} -o {2} {3} {4}',  # WPA2 ENTERPRISE
    'benchmark': '{} -b -m 2500'
}

"""
    download_file will download file with given [url] and [filename]
    -
    opens stream and if response code == 200 download file with chunk
    -
    returns 0 when OK
            2 not 200 response code
            3 in other cases
"""


def download_file(url, filename):
    try:
        r = requests.get(url, stream=True)
        # total_size = int(r.headers.get('content-length'))
        if r.status_code == 200:
            with open(filename, 'wb')as f:
                # shutil.copyfileobj(r.raw, f)
                for chunk in r.iter_content(chunk_size=1024):
                    if chunk:  # filter out keep-alive new chunks
                        f.write(chunk)
            f.close()
            r.close()
            return 0
        else:
            return 2
    except Exception as e:
        print("Exeption: {}".format(e))
        return 3


# Ungzip [input] to [output]
def ungzip(input, output):
    with gzip.open(input, 'rb') as f_in:
        with open(output, 'wb') as f_out:
            shutil.copyfileobj(f_in, f_out)


# Returns sha256(filename)
def calc_sha256(filename, block_size=256 * 128):
    h = hashlib.sha256()
    with open(filename, 'rb') as f:
        for chunk in iter(lambda: f.read(block_size), b''):
            h.update(chunk)
    return h.hexdigest()


# Returns 1 if hash(filename) == [hashsum], 0 if not
def check_hash(filename, hashsum):
    if calc_sha256(filename) == hashsum:
        return 1
    else:
        return 0


# Get job from server: request ot get_work_url and return content in json format
def get_job():
    try:
        r = requests.get(get_work_url)
        if r.status_code == 200:
            return r.json()
    except Exception as e:
        print("Failed to get job")
        exit(1)


# Send [content] to put_work_url
def put_job(content):
    r = requests.post(put_work_url, json=content)
    if r.status_code == 200:
        return 1
    else:
        return 0


def put_alive():
    while True:
        r = requests.post(alive_url, json={"alive": user_key})
        time.sleep(60)


# Check folders
if not os.path.exists(dict_folder):
    os.makedirs(dict_folder)
if not os.path.exists(hccapx_folder):
    os.makedirs(hccapx_folder)
if not os.path.exists(hash_folder):
    os.makedirs(hash_folder)
if os.path.exists(outfile):
    os.unlink(outfile)

# Check user key
if not os.path.exists(user_key_file):
    print("User key not specified in user_key.txt\nEnter key or type n to get key:\n")
    answer = input()
    if answer not in ['N', 'n']:
        user_key = answer
        with open(user_key_file, 'w') as f:
            f.write(user_key)
        f.close()
    else:
        import secrets, platform, re

        user_key = secrets.token_hex(16)
        with open(user_key_file, 'w') as f:
            f.write(user_key)
        f.close()

        print("Benchmarking...")
        runner = params['benchmark'].format(hashcat)
        try:
            o, e = subprocess.Popen(shlex.split(runner), stdout=subprocess.PIPE, stderr=subprocess.PIPE).communicate()
            o = o.decode('utf-8')

            speed = re.search(speed_regex, o)[0].split(' ')
            speed[0] = int(speed[0])
            if speed[1] == 'KH/s':
                speed[0] = speed[0] * 1000
            if speed[1] == 'MH/s':
                speed[0] = speed[0] * 1000000
            with open(benchmark, 'w') as f:
                f.write(str(speed[0]))
            f.close()
        except Exception as ex:
            print(ex)

        system_info = '{} {} {}'.format(platform.system(), platform.release(), platform.architecture()[0])

        r = requests.post(agents_url, json={'performance': speed[0], 'system_info': system_info, 'user_key': user_key})
        if r.status_code != 200:
            print("Unable to send data")
            exit(1)
else:
    with open(user_key_file, 'r') as f:
        user_key = f.readline().rstrip('\n')
    f.close()

t = threading.Thread(target=put_alive, daemon=True)
t.start()

try:
    job = {}
    while True:
        dict_queue = queue.deque()  # Queue for dictionaries
        brutefile = ''
        taskname = ''
        job_type = -1
        # if no job
        if len(job) == 0:
            job = get_job()
            print(job)
            if job['id'] == '-1':
                print("No tasks. Nothing to do.\nSleeping for 1 minute...")
                time.sleep(60)
                continue

            # Delete spaces in taskname
            taskname = ''.join(job['name'].split(' '))

            job_type = job['type']

            # Download hashes
            if job_type == '0':
                # Download brutefile and check hashsum
                brutefile = hccapx_folder + taskname + ".hccapx"
                download_file(job['url'], brutefile)
                if not check_hash(brutefile, job['hash']):
                    print("[ERROR] Checksums do not match")
                    exit(1)
            if job_type == '1':
                brutefile = hash_folder + taskname + '.txt'
                with open(brutefile, 'w') as f:
                    f.write('{}::::{}:{}'.format(job['username'], job['response'], job['challenge']))
                f.close()

            # Downaload all dicts and check hashsums
            for i in job['dicts']:
                # if i['dict_type'] == '0': # Temporary disable type checking
                filename = dict_folder + i['dict_url'].split('/')[-1]
                if not os.path.exists(filename):
                    print('Downloading {}'.format(filename))
                    download_file(i['dict_url'], filename)
                else:
                    if not check_hash(filename, i['dict_hash']):
                        print('Downloading {}'.format(filename))
                        download_file(i['dict_url'], filename)
                if not check_hash(filename, i['dict_hash']):
                    print("[ERROR] Checksums do not match. Exiting...")
                    exit(1)
                extension = filename.split('.')[-1]
                # Unpack dictionaries if necessary
                if extension == 'gz':
                    if not os.path.exists(''.join([i + '.' for i in filename.split('.')[:-1]])[:-1]):
                        print("Unpacking {}".format(filename))
                        ungzip(filename, ''.join([i + '.' for i in filename.split('.')[:-1]])[:-1])
                        # filename = ''.join([i + '.' for i in filename.split('.')[:-1]])[:-1]
                    dict_queue.append((filename[:-3], i['dict_id']))
                else:
                    dict_queue.append((filename, i['dict_id']))

        # run hashcat for every dict
        while len(dict_queue):
            # 0 - filename, 1 - dict_id
            i = dict_queue.popleft()  # i = current dictionary
            dict_path = i[0]
            dict_id = i[1]
            cracker = ''

            try:
                if job_type in ['0', '1']:
                    cracker = params[job_type].format(hashcat,
                                                      performance,
                                                      outfile,
                                                      brutefile,
                                                      dict_path)
                else:
                    exit(1)

                # Send status to api
                put_job({"job_status": "started",
                         "task_id": job['id'],
                         "dict_id": dict_id,
                         "user_key": user_key})

                # Run hashcat with arguments
                subprocess.check_call(shlex.split(cracker))

            # Catch exceptions and returncodes
            except subprocess.CalledProcessError as ex:
                if ex.returncode == -2:
                    print('[WARNING] Thermal watchdog barked')
                    print('Sleeping...')
                    dict_queue.appendleft(i)
                    time.sleep(120)
                    continue
                if ex.returncode == -1:
                    print('Internal error')
                    exit(1)
                if ex.returncode == 1:
                    print('[INFO] Exausted.')
                if ex.returncode == 2:
                    print('User abort')
                    exit(1)
                if ex.returncode not in [-2, -1, 1, 2]:
                    print('Cracker {0} died with code {1}'.format(hashcat, ex.returncode))
                    print('Unknown error occured')
                    exit(1)
            except KeyboardInterrupt as ex:
                print('\nKeyboard interrupt. Quiting...')
                # Cleanup
                if os.path.exists(outfile):
                    os.unlink(outfile)
                    exit(1)

            if os.path.exists(outfile):  # If bruted
                k = open(outfile, 'r')
                key = k.readline()
                k.close()
                key = key.rstrip('\n')
                # TODO: It seems that hashcat always create key file so this code can be refactored
                if len(key) >= 8:
                    print('Key found for job {0}:{1}'.format(job['name'], key))
                    while not put_job({'job_status': 'finished',  # Send key to server
                                       'task_id': job['id'],
                                       'dict_id': dict_id,
                                       'task_status': '2',
                                       'dict_status': '1',
                                       'net_key': key,
                                       'user_key': user_key}):
                        print("Can't submit key")
                        time.sleep(20)
                    os.unlink(outfile)
                    break
                else:
                    print("[INFO] Key for task {0} not found in {1} :(".format(job['name'], dict_path))
                    while not put_job({'job_status': 'finished',  # Send fail status
                                       'task_id': job['id'],
                                       'dict_id': dict_id,
                                       'task_status': '3',
                                       'dict_status': '1',
                                       'net_key': "",
                                       'user_key': user_key}):
                        print("Can't data to server")

            # cleanup
            if os.path.exists(outfile):
                os.unlink(outfile)

        # reset job
        print("Going to next job")
        job = {}
        time.sleep(2)
except KeyboardInterrupt as ex:
    print("Exiting...")
    exit(1)
