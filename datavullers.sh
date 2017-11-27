#!/bin/bash
dag=`date '+%Y-%m%d'` #Wat is de laatste file?
community="public"
#dir="/var/www/html/data" #Waar staat de data van HN?

#scp root@10.234.7.1:/var/www/html/data/data${dag}.csv /var/www/html/data/
#dos2unix /var/www/html/data/data${dag}.csv

### OPRUIMEN HUIDIGE DATA ###
rm -rf ./data/*
###

### SElinux goedzetten voor de data van SFlow ###
chcon -R -v --type=httpd_sys_content_t /var/spool/sflow/  > /dev/null 2>&1

### DATA FILES VULLEN ###
ls -l /var/spool/sflow/rrd/ | awk '{print $NF}' | grep rrd | awk -F- '{print $1}' | sort -u | grep -v "0.0.0.0" > ./data/uhosts

for i in `cat data/uhosts`
	do
	  name=`snmpwalk -v2c -c $community $i sysname | awk '{print $NF}'`
	  echo $name $i >> ./data/uhost_ip
        done

### VUL ONTBREKENEDE DATA AAN ###
for i in `cat data/uhost_ip | grep "^1"`
	do
	  echo $i > ./data/no-snmp-$i
	done

cat ./data/uhost_ip | grep -v "^1" > ./data/uhosts_ip

for i in `cat ./data/uhosts_ip | awk '{print $2}'`
        do
          if_name=`snmptable -v 2c -c $community -Cf , -Os $i .1.3.6.1.2.1.31.1.1 | grep ethernet | awk -F, '{print $1","$18","}' | sed 's/ethernet/___/g'`
          echo $if_name | sed 's/___/\n/g' | sed '/^$/d' > ./data/ifalias-$i
          if_index=`snmpwalk -v 2c -c $community $i ifName | grep ethernet | awk -F: '{print $3}' |  awk '{print $1}' | sed 's/ifName./___/' | sed 's/ //'`
          echo $if_index | sed 's/___/\n/g' | sed '/^$/d' | sed 's/, /,/g' > ./data/ifindex-$i 
	  paste ./data/ifalias-$i ./data/ifindex-$i | sed 's/\t//' >> ./data/iftotaal-$i 
        done

cat hosts-aanvul >> ./data/uhosts_ip

for file in `ls -l ./data/iftotaal* | awk '{print $NF}'`
do
	cat $file | sed 's/, /,/g' > $file-rhg
done

mysqldump -usflow -psflowtool sflow >> sflow-$dag.sql
mysql -usflow -psflowtool -Dsflow -e "DELETE from sflowtool where id != '';)";

for file in `ls -l /var/spool/sflow/rrd/ | awk '{print $NF}' | grep rrd | grep -v "0.0.0.0"`
do 
#echo "Filenaam: $file";
ip=`echo $file | awk -F\- '{print $1}'`;
hostname=`grep "$ip$" ./data/uhosts_ip | awk '{print $1}'`;
portindex=`echo $file | awk -F\- '{print $2}' | awk -F\. '{print $1}'`;
slotport=`grep ",$portindex $" ./data/iftotaal-$ip-rhg | awk -F, '{print $1}'`;
label=`grep ",$portindex $" ./data/iftotaal-$ip-rhg | awk -F, '{print $2}'`;
echo "IP is: $ip Host is: $hostname Portindex is: $portindex SlotPort is: $slotport Met Label: $label" >> vulling-$dag;

query=`echo INSERT INTO sflowtool SET filename=\'$file\', ip=\'$ip\', hostname=\'$hostname\', portindex=\'$portindex\', slotport=\'$slotport\', iflabel=\'$label\'\;`;
mysql -usflow -psflowtool -Dsflow -e "${query}";
done
