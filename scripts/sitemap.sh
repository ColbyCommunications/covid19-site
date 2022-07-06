EDGE_HOSTNAME=$(~/.platformsh/bin/platform environment:info edge_hostname)
JQ="https://${EDGE_HOSTNAME}"
echo $(cat .github/sitemap.json | jq -r --arg JQ "$JQ" '.urls += [$JQ]') > .github/sitemap.json
flatten_sitemap --sitemap https://$EDGE_HOSTNAME/sitemap_index.xml --config .github/sitemap.json --limit 25 --randomize