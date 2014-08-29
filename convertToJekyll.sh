#!/bin/bash
dir="$1"
output="$2"
cd "$dir"

if [[ -z $dir || -z $output ]]; then
    echo "Usage: convertToJekyll.sh flowgen/pages jekyll/"
    exit 0
fi

mkdir -p "$output/_posts"
mkdir -p "$output/_pages"
mkdir -p "$output/_drafts/posts"
mkdir -p "$output/_drafts/pages"

getData() {
    local i="$1"
    title="$(grep "^Title: " "$i" | sed 's/^Title: //' | sed 's/"/\\\"/g')"
    created="$(grep "^Created: "   "$i" | sed 's/^Created: //'  | awk '{print strftime("%Y-%m-%d %T %Z",$1)}')"
    modified="$(grep "^Modified: " "$i" | sed 's/^Modified: //' | awk '{print strftime("%Y-%m-%d %T %Z",$1)}')"
    [[ -z $modified ]] && modified="$(stat -c %Y "$i" | awk '{print strftime("%Y-%m-%d %T %Z",$1)}')"
    url="$(grep "^URL: " "$i" | sed 's/^URL: //')"
    date="$(sed -r 's/^.*\/?([0-9]{4})([0-9]{2})([0-9]{2})_.*$/\1-\2-\3/g' <<< "$i")"
    content="$(sed '1,/^-$/ d' "$i" | sed '
        s/{{soundcloud:\([^}]*\)}}/{% soundcloud \1 %}/g
        s/{{music:\([^,]*\),\([^}]*\)}}/{% music \1, \2 %}/g
        s/{{music_player:\([^,]*\)}}/{% music_player \1 %}/g
        s/{{photo:\([^,]*\),\([^}]*\)}}/{% photo \1, \2 %}/g
        s/{{photo_plain:\([^,]*\),\([^}]*\)}}/{% photo_plain \1, \2 %}/g
        s/{{photo_png:\([^,]*\),\([^}]*\)}}/{% photo_png \1, \2 %}/g
        s/{{photo_page:\([^,]*\),\([^}]*\)}}/{% photo_page \1, \2 %}/g
        s/{{photo_page:\([^,]*\)}}/{% photo_page \1 %}/g
        s/{{youtube:\([^,]*\)}}/{% youtube \1 %}/g
    ')"
}

for i in *.txt; do
    getData "$i"

    if grep '^/blog' <<< "$url" &>/dev/null; then
        blogName="$(sed 's#/blog/##g' <<< "$url")"
        filename="_posts/$date-$blogName.html"
        cat > "$output/$filename" <<EOF
---
title: "$title"
date: $created
permalink: $url/
layout: post
---
$content
EOF
    else
        name="$(sed 's#/##g' <<< "$url")"
        filename="_pages/$name.html"
        cat > "$output/$filename" <<EOF
---
title: "$title"
date: $modified
permalink: $url/
---
$content
EOF
    fi

    echo "Saving: $filename"
done


if [[ -d "old" ]]; then
    cd "old"
    for i in *; do
        getData "$i"

        if grep '^/blog' <<< "$url" &>/dev/null; then
            blogName="$(sed 's#/blog/##g' <<< "$url")"
            filename="_drafts/posts/$date-$blogName.html"
            cat > "$output/$filename" <<EOF
---
title: "$title"
date: $created
permalink: $url/
layout: post
---
$content
EOF
        else
            name="$(sed 's#/##g' <<< "$url")"
            filename="_drafts/pages/$name.html"
            cat > "$output/$filename" <<EOF
---
title: "$title"
date: $modified
permalink: $url/
---
$content
EOF
        fi

        echo "Saving: $filename"
    done
fi
