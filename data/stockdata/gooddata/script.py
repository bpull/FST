import last20 as l20
import csv

tickers = []
with open('/home/m175148/git/FST/data/stockdata/gooddata/tickers.txt') as csvfile:
    reader = csv.reader(csvfile, delimiter='\t')
    for row in reader:
        tickers.append(row[0])

for tick in tickers:
    try:
        l20.do_it(tick)
    except Exception as e:
        print e
        pass
