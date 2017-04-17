#/usr/bin/env python3
import shlex
import subprocess
import requests
import json
import shutil
import os
import sys
import hashlib
import time

#API conf
base_url = 'https://inlovewith.space/'
get_work_url = base_url + '?get_work'
put_work_url = base_url + '?put_work'
api_key = ''

#Hashcat conf
hashcat = 'hashcat64.exe'
performance = '-w 3'
outfile = 'pass.key'


"""
    download_file will download file with given [url] and [filename]
    -
    opens stream and if response code == 200 pipe it to shutil.copyfileobj()
    -
    returns 0 when OK
            1 when url not specified [or wrong //TODO fetch execpiton from request.get()
            2 when filename not specified [or wrog //TODO check filename or fetch exeption from open()
            3 when failed to connect to server
"""

#TODO: add checks and exeptions cather


def download_file(url=None,filename=None,compressed=False):
    if url == None or filename == None:
        return 1
    try:
        r = requests.get(url,stream=True)
        r.raw.decode_content = True
        if r.status_code == 200:
            with open(filename, 'wb')as f:
                shutil.copyfileobj(r.raw, f)
            f.close()
            r.close()
            return 0
        else:
            return 3
    except Exception as e:
        print("Exeption: {}".format(e))


def calc_sha256(filename, block_size=256*128):
    h = hashlib.sha256()
    with open(filename, 'rb') as f:
        for chunk in iter(lambda: f.read(block_size), b''):
            h.update(chunk)
    return h.hexdigest()


def check_hash(filename: str, hashsum: str) -> int:
    if calc_sha256(filename) == hashsum:
        return 1
    else:
        return 0


#Get job from server: request ot get_work_url and return content in json format
def get_job():
    r = requests.get(get_work_url)
    if r.status_code == 200:
        return r.json()


def put_key(id,key):
    r = requests.post(put_work_url,json={{'id': id}, {'key': key}})
    if r.status_code == 200:
        return 1
    else:
        return 0


#Parse json and return
def prepare_work(j):
    return {'id': j['id'], 'hccap_file': j['hccap'],'hccap_url': j['hccap_url'], 'dicts': j['dicts']}


job = None
current_job = None

while True:
    dicts = ""
    #prepare raw job response from server
    if job is None:
        job = get_job()
        current_job = prepare_work(job)
        for i in current_job['dicts']:
            if not os.path.exists(i['filename']):
                download_file(i['url'], i['filename'])
                if not check_hash(i['filename'], i['hash']):
                    print("[ERROR] Checksums do not match. Exiting...")
                    exit(1)
                #TODO:add try-catch and exeptions to functions
            else:
                if not check_hash(i['filename'], i['hash']):
                    download_file(i['url'], i['filename'])
                    if not check_hash(i['filename'], i['hash']):
                        print("[ERROR] Checksums do not match. Exiting...")
                        exit(1)
    handshake = current_job['hccap_file']
    for i in current_job['dicts']:
        dicts += '"'+i['filename']+'" '

    try:
        cracker = '{0} -m2500 --potfile-disable --outfile-format=2 {1} -o{2} {3} {4}'.format(hashcat, performance, outfile, handshake, dicts) #TODO:add rules support
        try:
            subprocess.check_call(shlex.split(cracker))
        except subprocess.CalledProcessError as ex:
            if ex.returncode == -2:
                print('[WARNING] Thermal watchdog barked')
                print('Sleeping...')
                time.sleep(222)
                continue
            if ex.returncode == -1:
                print
                ('Internal error')
                exit(1)
            if ex.returncode == 1:
                print
                ('Exausted')
            if ex.returncode == 2:
                print
                ('User abort')
                exit(1)
            if ex.returncode not in [-2, -1, 1, 2]:
                print
                ('Cracker {0} died with code {1}'.format(hashcat, ex.returncode))
                print
                ('Check you have CUDA/OpenCL support')
                exit(1)
    except KeyboardInterrupt as ex:
        print('\nKeyboard interrupt')
        if os.path.exists(outfile):
            os.unlink(outfile)
        exit(1)

    if os.path.exists(outfile):
        k = open(outfile, 'r')
        key = k.readline()
        k.close()
        key = key.rstrip('\n')
        if len(key) >= 8:
            print('Key found for {0}:{1}'.format(current_job['ssid'],key))
            while not put_key(current_job['id'], key):
                print("Can't submit key")
                time.sleep(222)

        os.unlink(outfile)
    else:
        print("TODO: send server signal for fail, need to write api")

    #cleanup
    if os.path.exists(outfile):
        os.unlink(outfile)


