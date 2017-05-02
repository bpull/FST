import withnews as newsnet
import withoutnews as net
import csv

tickers = []
with open('/home/m175148/git/FST/data/stockdata/gooddata/tickers.txt') as csvfile:
    reader = csv.reader(csvfile, delimiter='\t')
    for row in reader:
        tickers.append(row[0])

for tick in tickers:
    try:
        newsnet.run_net(500, 250, .001, 0, tick)
    except Exception as e:
        pass

for tick in tickers:
    try:
        net.run_net(500, 250, .001, 0, tick)
    except Exception as e:
        pass
