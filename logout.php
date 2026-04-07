<?php
// 1. Tangira session kugira ngo ubone izo ushaka gusiba
session_start();

// 2. Siba amakuru yose yari abitse muri $_SESSION variable
$_SESSION = array();

// 3. Niba hari cookie ya session mu mushakashanyi (browser), iyisibe (Security Best Practice)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Senya session burundu kuri server
session_destroy();

// 5. Ohereza umuntu kuri page yo kwinjira (Login page)
header("Location: index.php");
exit();
?>