<?php
// Legacy redirect — all logic moved to auth_reset_password.php
header('Location: auth_reset_password.php?' . http_build_query($_GET));
exit;
