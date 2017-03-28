fp = open("tickers.txt","r")
for line in fp:
    print(" ".join(line.split()).split(" ")[0])
