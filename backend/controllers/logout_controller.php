<?php
require_once('../models/users_admin_model.php');
require_once('../../config/db.php');
$user = new User($connection);
$admin= new Admin($connection);
session_start();
if(isset($_SESSION['admin_email'])){
    $log_out = $admin->logout_admin();

}elseif($_SESSION['user_email']){
    $log_out = $user->logout_user(); 
}
if($log_out){
    echo json_encode([
    'status' => 'success',
    'data' => 'Logged out successfully.'
]);
exit;
}else{
    echo json_encode([
    'status' => 'error',
    'data' => 'something went wrong logging out. Try again.'
]);
exit;
}



?>