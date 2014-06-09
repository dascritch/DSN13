#!/bin/bash
scss -t compact scss/source.scss compressed.css

rm concatenated.js

cat js/jquery2.js js/jquery.cookie.js js/jquery.lightbox.X.js js/timecodehash.js js/ductile.js > concatenated.js

cp concatenated.js compressed.js
java -jar /usr/share/java/closure-compiler.jar --language_in ECMASCRIPT5 --compilation_level SIMPLE_OPTIMIZATIONS  --js concatenated.js --js_output_file compressed.js

git add compressed.css concatenated.js compressed.js

# enregistrer la date à inclure dans l'appel des assets pour prévenir les navigateurs d'un changement de version
BUILDDATE=`date +%d%m%Y`
sed -i "s/^\(<\!-- # BUILDDATE -->.*?\)\([0-9]\{8\}\)/\1${BUILDDATE}/Mg" tpl/_head.html



