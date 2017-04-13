<div class="container">



        <div class="col-md-8">
            <h2>Wordlist</h2>
            <div class="panel panel-default">
                <table class="table table-striped table-bordered table-nonfluid">
                    <tbody>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Word count</th>
                            <th>Size</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4">
            <h3>Add new wordlist</h3>
            <form class="" action="files.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="source" value="upload">
                <input type="hidden" name="action" value="addfile">
                <div class="panel panel-default">
                    <table class="table table-bordered table-nonfluid">
                        <tbody>
                            <tr>
                                <th>Download files</th>
                            </tr>
                            <tr>
                                <td>
                                    <input type="file" class="form-control" name="upfile[]">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="submit" class="btn btn-default" value="Upload files">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>

            <form action="?dicts" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="addfile">
                <input type="hidden" name="source" value="url">
                <div class="panel panel-default">
                    <table class="table table-bordered table-nonfluid" id="upfiles">
                        <tbody>
                            <tr>
                                <td colspan="2">Download URL</td>
                            </tr>
                            <tr>
                                <td>URL:</td>
                                <td>
                                    <input type="text" class="form-control" name="url">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" class="btn btn-default" value="Download file">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

    </div>