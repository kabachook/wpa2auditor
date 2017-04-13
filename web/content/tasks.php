<div class="container">
  	<h2>Tasks</h2>
    <div style="overflow: auto;">
		    <form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST">
			       <input type="hidden" name="action" value="finishedtasksdelete">
			          <input type="submit" value="Delete finished" class="btn btn-default">
		    </form>

	  <div style="overflow: auto;">
           <form style="float: left; padding-right: 5px;" action="" class="form-inline" method="POST">
              <input type="hidden" name="toggleautorefresh" value="On">
              <input type="submit" value="Turn on auto-reload" class="btn btn-success">
            </form>
        <br>

      </div>
	       <br>
      </div>


      	<div class="panel panel-default">
      	<table class="table table-striped table-bordered table-nonfluid">
      		<tbody>
      			<tr>
      				<th>ID</th>
      				<th>Name</th>
      				<th>Progress</th>
      				<th>Files</th>
      			</tr>
      		</tbody>
      	</table>
      	</div>
      </div>