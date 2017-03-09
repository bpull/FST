#!/bin/bash

echo "Enter year of news data"
read year
mkdir "$year-data"
for i in {1..12}; do
    python tradeCalendar2.py $i $year | python nyt2.py > "$year-data/$i"
done
