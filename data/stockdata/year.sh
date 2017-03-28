#!/bin/bash

echo "Enter company stock symbol"
read symbol
echo "Enter year of stock data"
read year
mkdir "stockdata/$symbol/$year-data/"
for i in {1..12}; do
    python tradeCalendar2.py $i $year | python stockData.py $symbol > "stockdata/$symbol/$year-data/$i"
done
