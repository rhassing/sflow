#!/bin/bash

agent=$1
if=$2

if [ -z "$agent" -o -z "$if" ] || [ -n "$c" -a -z "$w" ]
then
        echo usage: "$0 <ipaddress> <port number> [<linewidt> [<critical> <warning>]]"
        exit $ERROR
fi

speed=$3
c=$4
w=$5

c=${c:-30}
w=${w:-25}

case $speed in
0)
        lw=1
        ;;
1)
        lw=1000000
        ;;
10)
        lw=10000000
        ;;
100)
        lw=100000000
        ;;
1000)
        lw=1000000000
        ;;
10000)
        lw=10000000000
        ;;
100000)
        lw=100000000000
        ;;
*)
esac


#lw=speed

file=ifdata/$agent-$if.rrd

if [ -f "$file" ] 
then

	if ! data=$(rrdtool fetch $file AVERAGE -s -360 -e -120  | tail -5)
	then
        	echo Could not fetch rrd data from $file
	        exit $UNKNOWN
	fi
	set $data
		while [ -n "$1" ]
		do
		        t=$1
		        in=$2
		        out=$3
		        shift 3
		#       l=${l#*: }
		#       in=${l%% *}

		        ine=${in##*e+}
		        inm=${in%%e*}
		        inm=$(echo "$inm * 10 ^ $ine" | bc)
		        in=${inm%%.*}
		
		        (( sin += in ))
		
		        oute=${out##*e+}
		        outm=${out%%e*}
		        outm=$(echo "$outm * 10 ^ $oute" | bc)
		        out=${outm%%.*}
		
		        (( sout += out ))
		#echo $sin $sout
		done

		#echo $sin $sout
		((ain = sin * 8 / 5 ))
		((aout = sout * 8 / 5 ))
		((pin = ain * 100 / lw))
		((pout = aout * 100 / lw))

		if [[ $pin -gt 30 ]] && [[ $pout -gt 30 ]]; 
		then
			echo "<td bgcolor=orange>in: $pin%</td><td bgcolor=orange>out: $pout%</td>"
			exit
		fi

		if [[ $pin -lt 30 ]] && [[ $pout -lt 30 ]]; 
		then
			echo "<td>in: $pin%</td><td>out: $pout%</td>"
			exit
		fi

		if [[ $pin -gt 30 ]] || [[ $pout -gt 30 ]]; 
		then
			if [[ $pin -gt 30 ]]; 
			then
				echo "<td bgcolor=orange>in: $pin%</td><td>out: $pout%</td>"
			else 
				echo "<td>in: $pin%</td><td bgcolor=orange>out: $pout%</td>"
			fi
		fi

		
else
	echo "<td>in:</td><td>out:</td>"
fi

