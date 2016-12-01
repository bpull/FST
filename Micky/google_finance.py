from __future__ import print_function
from googlefinance import getQuotes,getNews


print("Please select which company you would like to research")
print("1) Google") # GOOGL
print("2) Apple")  # APPL
print("3) Yahoo")  # YHOO
print("4) Tesla")  # TSLA
print("5) Amazon") # AMZN
picked = False
company = "n/a"
while not picked:
    which_company = raw_input()
    which_company = int(which_company)
    if which_company == 1:
        company = "GOOGL"
    elif which_company == 2:
        company = "AAPL"
    elif which_company == 3:
        company = "YHOO"
    elif which_company == 4:
        company = "TSLA"
    elif which_company == 5:
        company = "AMZN"
    else:
        print("Please only use a number between 1 and 5 - ",end="")
    if company != "n/a":
        picked = True

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




if want_news:

    print("\ngathering the news from ",end="")
    try:
        news = getNews(company)
        print()
        counter = 0
        for article in news:
            print(article["t"])
            counter += 1
            if counter == how_many:
                break
    except:
        print("\nSorry, an error occured in the google search")
    print()
quote = getQuotes(company)
for item in quote[0]:
    print(item,"=",quote[0][item])
print()
