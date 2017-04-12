fp = open("tickers.txt","r")
fp2=open("companies.txt","w")
for line in fp:
    newlist = []
    for word in line.split():
        if word == "reports":
            break
        else:
            newlist.append(word)
    fp2.write(newlist.pop(0) + " "+" ".join(newlist)+"\n")
