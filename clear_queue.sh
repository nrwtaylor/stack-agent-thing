for MATCH in $(echo status | nc 127.0.0.1 4730 | grep -v \\. |  grep -Pv '^[^\t]+\t0\t' | cut -s -f 1-2 --output-delimiter=\,); 
do 
    gearman -n -w -f ${MATCH%\,*} -c ${MATCH#*\,} > /dev/null;
done
