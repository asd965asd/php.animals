<?php
// index.php — единая точка входа (Router)
require_once __DIR__ . '/config/bootstrap.php';

$page = isset($_GET['page']) ? (string)$_GET['page'] : 'home';

switch ($page) {
    case 'home':
        require __DIR__ . '/actions/home.php';
        $title = 'Главная';
        render(__DIR__ . '/templates/home.php', compact('title', 'q', 'district', 'posts', 'currentPage', 'totalPages'));
        break;

    case 'login':
        require __DIR__ . '/actions/auth/login.php';
        $title = 'Вход';
        render(__DIR__ . '/templates/auth/login.php', compact('title', 'errorMsg'));
        break;

    case 'register':
        require __DIR__ . '/actions/auth/register.php';
        $title = 'Регистрация';
        render(__DIR__ . '/templates/auth/register.php', compact('title', 'errorMsg', 'successMsg'));
        break;

    case 'profile':
        require __DIR__ . '/actions/profile/index.php';
        $title = 'Личный кабинет';
        render(__DIR__ . '/templates/profile/index.php', compact('title', 'avatarUrl', 'my_orders', 'profileError'));
        break;

    case 'admin':
        require __DIR__ . '/actions/admin/panel.php';
        $title = 'Админка';
        render(__DIR__ . '/templates/admin/panel.php', compact('title'));
        break;

    case 'add_post':
        require __DIR__ . '/actions/admin/add_post.php';
        $title = 'Добавить объявление';
        render(__DIR__ . '/templates/admin/add_post.php', compact('title', 'messageHtml'));
        break;

    case 'edit_post':
        require __DIR__ . '/actions/admin/edit_post.php';
        $title = 'Редактировать объявление';
        render(__DIR__ . '/templates/admin/edit_post.php', compact('title', 'messageHtml', 'post'));
        break;

    case 'delete_post':
        require __DIR__ . '/actions/admin/delete_post.php';
        break;

    case 'admin_orders':
        require __DIR__ . '/actions/admin/orders.php';
        $title = 'Заявки';
        render(__DIR__ . '/templates/admin/orders.php', compact('title', 'orders'));
        break;

    case 'admin_lost_requests':
        require __DIR__ . '/actions/admin/lost_requests.php';
        $title = 'Заявки владельцев';
        render(__DIR__ . '/templates/admin/lost_requests.php', compact('title', 'lostRequests'));
        break;

    case 'admin_lost_request_decide':
        require __DIR__ . '/actions/admin/lost_request_decide.php';
        break;

    case 'admin_seeder':
        require __DIR__ . '/actions/admin/seeder.php';
        $title = 'Seeder';
        render(__DIR__ . '/templates/admin/seeder.php', compact('title', 'seedMessage'));
        break;

    case 'create_lost_request':
        require __DIR__ . '/actions/requests/create.php';
        $title = 'Заявка о пропаже';
        render(__DIR__ . '/templates/requests/create.php', compact('title', 'requestMessageHtml'));
        break;

    case 'make_order':
        require __DIR__ . '/actions/orders/make_order.php';
        break;

    case 'cancel_order':
        require __DIR__ . '/actions/orders/cancel.php';
        break;

    case 'order_details':
        require __DIR__ . '/actions/profile/order_details.php';
        $title = 'Детали заявки';
        render(__DIR__ . '/templates/profile/order_details.php', compact('title', 'order', 'img'));
        break;

    case 'change_password':
        require __DIR__ . '/actions/profile/change_password.php';
        $title = 'Смена пароля';
        render(__DIR__ . '/templates/profile/change_password.php', compact('title', 'errorMsg', 'successMsg'));
        break;

    case 'logout':
        require __DIR__ . '/actions/auth/logout.php';
        break;

    default:
        http_response_code(404);
        echo '404';
        break;
}