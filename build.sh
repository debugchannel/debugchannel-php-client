#! /bin/bash

dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
destfile=dist/D.php
srcfiles=(src/D.php src/LanguageAgnosticParser.php RHtmlSpanFormatter.php src/ref.php)

echo "" > $destfile
for file in "${srcfiles[@]}"
do
	cat $file >> $destfile
done
