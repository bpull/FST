import yahoo_finance as yf
from pprint import pprint
'''
WMT = walmart
GOOGL = Google
AAPL = apple
F = Ford
XOM = exxon mobil
'''
fp = open("companies.txt","r")
companies = []
for line in fp:
    companies.append(yf.Share(line.strip()))
comp_stock = []
for comp in companies:
    comp_stock.append(comp.get_historical('2012-01-01','2017-03-07'))
for comp in comp_stock:
    # pprint(comp)
    for entry in comp:
        print(entry["Symbol"]+" "+ entry["Date"]+" "+entry["Close"])
