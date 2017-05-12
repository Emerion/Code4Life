echo "<?php\n"
find . -name "*.php" -print0 | xargs -0 cat | grep -v "?php"
