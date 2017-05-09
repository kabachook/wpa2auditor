<div class="container-fluid">
	<div class="col-lg-9 col-lg-offset-1">
		<h2>Search</h2>
		<div class="panel panel-default">
			<table class="table table-striped table-bordered table-nonfluid">
				<tbody>
					<tr>
						<th>#</th>
						<th>MAC</th>
						<th>Task name</th>
						<th>Net name</th>
						<th>Key</th>
						<th>Files</th>
						<th>Agents</th>
						<th>Status</th>
					</tr>
					<?php

					$essid = $_POST[ 'search_query' ];

					//List of status codes for tasks
					function getStatus( $status ) {
						$listOfStatus = [
							0 => "IN QUEUE",
							1 => "IN PROGRESS",
							2 => "SUCCESS",
							3 => "FAILED",
						];
						return $listOfStatus[ $status ];
					}

					//Show tasks from DB
					$sql = "SELECT id, name, filename, status, agents, net_key, essid, station_mac, site_path FROM tasks WHERE essid='" . $essid . "'";
					$result = $mysqli->query( $sql )->fetch_all( MYSQLI_ASSOC );

					$id = 0;
					foreach ( $result as $row ) {
						if ( $row[ 'net_key' ] == '0' ) {
							$key = '<input type="text" class="form-control" placeholder="Enter wpa key" name="' . $row[ 'id' ] . '">';
						} else {
							$key = "<strong>" . $row[ 'net_key' ] . "</strong>";
						}
						$id++;
						$str = '<tr><td><strong>' . $id . '</strong></td><td>' . $row[ 'station_mac' ] . '</td><td>' . $row[ 'name' ] . '</td><td>' . $row[ 'essid' ] . '</td><td>' . $key . '</td><td><a href="' . $row[ 'site_path' ] . '" class="btn btn-default"><span class="glyphicon glyphicon-download"></span></a><td>' . $row[ 'agents' ] . '</td><td class="status">' . getStatus( $row[ 'status' ] ) . '</td>';
						$tasks_admin_panel = '<td><a class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></a></td></tr>';
						echo $str;
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>