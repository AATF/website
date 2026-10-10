<?php
print(explode(",", htmlspecialchars($_SERVER["HTTP_X_FORWARDED_FOR"], ENT_QUOTES, 'UTF-8'))[0]);
?>
