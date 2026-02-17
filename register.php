<?php
// Обертка для старых ссылок:
// теперь регистрация обрабатывается через index.php?route=register (MVC-роутер).

header('Location: index.php?route=register');
exit;