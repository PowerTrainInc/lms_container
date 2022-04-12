#/usr/bin/bash

FILE=/tmp/env1
if [ ! -f $FILE ]; then
for variable_value in $(cat /proc/1/environ | sed 's/\x00/\n/g'); do
    echo "export $variable_value" >> /tmp/env1
done
fi