echo "<?php\n"
find . -name "*.php" -not -name "build*.php" -print0 | xargs -0 cat | grep -v "?php"
