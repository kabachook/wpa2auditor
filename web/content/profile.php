<?php
$sql = "SELECT * FROM users WHERE userkey=UNHEX('" . $_COOKIE['key'] . "')";
$result = $mysqli->query($sql)->fetch_object();

echo "Your userkey is " . bin2hex($result->userkey) . "<br />";
echo "Your invite is " . bin2hex($result->invite) . "<br />";
echo "You invite " . $result->invited_c . " users";

?>


<div class="container">
    <div class="span3 well">
        <center>
        <a href="#aboutModal" data-toggle="modal" data-target="#myModal"><img src="https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcRbezqZpEuwGSvitKy3wrwnth5kysKdRqBW54cAszm_wiutku3R" name="aboutme" width="140" height="140" class="img-circle"></a>
        <h3>Joe Sixpack</h3>
        <em>click my face for more</em>
		</center>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="myModalLabel">My profile</h4>
                    </div>
                <div class="modal-body">
                    <p>Your userkey is <strong><?php echo bin2hex($result->userkey); ?> </strong></p>
                    <p>Your invite is <strong><?php echo bin2hex($result->invite); ?> </strong></p>
                    <p>You invite <strong><?php echo $result->invited_c; ?></strong> users</p>
                </div>
                <div class="modal-footer">
                    <center>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close profile</button>
                    </center>
                </div>
            </div>
        </div>
    </div>
</div>