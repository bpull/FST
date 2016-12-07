from __future__ import print_function
from googlefinance import getQuotes,getNews

#TODO add time. don't let run after 4pm and before 9am

def newsGet(company,fp):
    print("\ngathering the news from ",end="")
    try:
        news = getNews(company)
        print()
        counter = 0
        for article in news:
            print(article["t"])
            try:
                fp.write(article["t"]+"\n")
            except:
                pass
            counter += 1
            if counter == how_many:
                break
    except:
        print("\nSorry, an error occured in the google search")

def quotes(company,fp):
    try:
        quote = getQuotes(company)
    except:
        print("Can not get financial data for this company")

    for item in quote[0]:
        fp.write(item+"="+quote[0][item]+"\n")
        print(item,"=",quote[0][item])

def getStockSymbols():
    fp = open("nasdaqlisted.txt","r")
    symbols = []
    for line in fp:
        word = []
        for letter in line:
            if letter != "|" and letter != ".":
                word.append(letter)
            else:
                break
        word = ''.join(word)
        symbols.append(word)
    return symbols

symbols = getStockSymbols()
picked = False
company = "n/a"

print("Please enter a company's stock symbol - ",end="")
while not picked:
    company = raw_input()
    if company in symbols:
        print("Looking up stock data for",company)
        picked = True
    else:
        print("Company not found, please try again - ",end="")

news = False
want_news = False
while not news:
    print("\nWould you like to recieve news headlines about this company as well: yes/no - ",end="")
    answer = raw_input()
    if answer == "yes" or answer == "y":
        correct = False
        while not correct:
            print("\nHow many would you like? (1:999) - ",end="")
            how_many = raw_input()
            how_many = int(how_many)
            if how_many > 0 and how_many < 1000:
                correct = True
        want_news = True
        news = True
    elif answer == "no" or answer == "n":
        news = True

fp = open("financialdata"+company+".txt","w")

if want_news:
    newsGet(company,fp)


    print()
quotes(company,fp)
print()
