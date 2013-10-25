#! /bin/bash

dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
destfile=dist/D.php
srcprefix=src/debugchannel/
srcfiles=(D.php RHtmlSpanFormatter.php ref.php)

rm $destfile
touch $destfile
for file in "${srcfiles[@]}"
do
	cat "${srcprefix}${file}" >> $destfile
	echo "" >> $destfile
done
