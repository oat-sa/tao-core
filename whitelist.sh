




whitelist=$(git diff origin/develop --name-only -- '*.php' | xargs -IX echo -n "<FILE>X</FILE>")
sed -e "s%{WHITELISTED_FILES}%$whitelist%g" phpunit_full.xml | tidy -xml -qi