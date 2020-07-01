
git --no-pager diff origin/develop --name-only | while read -r x; do
  echo "<FILE>$x</FILE>"
done

