<?php
header('Location: index.php?page=order_details&id=' . (int)($_GET['id'] ?? 0));
exit;

