#!/bin/bash

DIRS="./inc/bx"
for dir in $DIRS
do
OLD=`md5sum div/phpfiles | cut -d " " -f 1` 
mv div/phpfiles div/phpfiles.old
for i in `find $dir -name "*.php" ` ; do md5sum $i >> div/phpfiles; done 
NEW=`md5sum div/phpfiles| cut -d " " -f 1`
echo $NEW;
echo $OLD;
if test $NEW != $OLD
then
date=`date +%Y%m%d%H%M`; sed -e "s/BXCMS_BUILD','.*/BXCMS_BUILD','$date');/" inc/bx/init.php > inc/bx/init.php.new ; mv inc/bx/init.php.new inc/bx/init.php
svn ci -m"automatic BuildDate Update" inc/bx/init.php
rm div/phpfiles
for i in `find $dir -name "*.php"` ; do md5sum $i >> div/phpfiles; done 
fi
done
