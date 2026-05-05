<?php
session_start();

// clear all session data
session_unset();
session_destroy();

// redirect with success flag
header("Location: login.php?logout=success");
exit;