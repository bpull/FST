from yahoo_finance import Share
from pprint import pprint
import sys,ast
import re

if __name__ == "__main__":
    symbol = str(sys.argv[1])
    amazon = Share(symbol)
    #with open(sys.argv[1],'r') as f:
    #    dates = f.read()
    dates = sys.stdin.read()
    dates = re.sub('[[\]\']','',dates)
    dates = [x.strip() for x in dates.split(',')]
    firstDay = dates.pop(0)
    lastDay = dates[-1]
    stockData = amazon.get_historical(firstDay,lastDay)
    currentInfo = stockData.pop(-1)
    result = []
    for x in reversed(stockData):
        #Previous day volatility
        volatility = float(currentInfo['High']) - float(currentInfo['Low'])
        change = float(currentInfo['Close']) - float(currentInfo['Open'])
        overnightChange = float(x['Open']) - float(currentInfo['Close'])
        resultChange = float(x['Close']) - float(x['Open'])
        tup = (volatility,change,overnightChange,resultChange)
        result.append(tup)
        currentInfo = x
    print result

        #For each date we want to get the stock prices for the range in question
    #pprint(amazon.get_historical('2017-02-01','2017-02-04'))
