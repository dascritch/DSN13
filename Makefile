#!/bin/bash
scss -t compact source.scss compressed.css

rm concatenated.js

cat jquery.js jquery.lightbox.X.js ductile.js > concatenated.js
java -jar /usr/share/java/closure-compiler.jar --language_in ECMASCRIPT5 --compilation_level SIMPLE_OPTIMIZATIONS  --js concatenated.js --js_output_file compressed.js

# todo : enregistrer la date pour le numéro de version à servir en public