#!/bin/bash
cd "/home/garrett/Documents/Floft" || exit 1
dirs="pages"

dups="$(grep -R 'URL: ' $dirs | grep -v '~' | cut -d: -f3 | sed -r 's/.*\///g' | awk 'arr[$0]++; END {for(i in arr ){ if(arr[i]>1){print i} } }' | sort -u)"

for url in $dups; do
	grep -RE "URL: .*$url$" $dirs | cut -d: -f1 | grep -v '~'
	echo
done
