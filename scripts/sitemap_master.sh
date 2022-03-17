EDGE_HOSTNAME=$(~/.platformsh/bin/platform domains --format=plain --no-header --columns=name )
JQ="https://${EDGE_HOSTNAME}"
echo $(cat .github/sitemap.json | jq -r --arg JQ "$JQ" '.urls += [$JQ]') > .github/sitemap.json
cat .github/sitemap.json
