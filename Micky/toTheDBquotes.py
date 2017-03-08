from __future__ import print_function
import mysql.connector
from mysql.connector import Error
import time, datetime,sys
from googlefinance import getQuotes,getNews

def quotes(company):
    try:
        quote = getQuotes(company)
    except:
        print("Can not get financial data for this company")

    return quote[0]["LastTradePrice"]
    #for item in quote[0]:
    #    print(item,"=",quote[0][item])

def connect():
    if datetime.datetime.now().hour + 3 >= 16:
        print("The market has closed")
        sys.exit()
    """ Connect to MySQL database """
    now = time.time()
    ltp = quotes("GOOGL")
    symbol = "GOOGL"
    try:
        conn = mysql.connector.connect(host='localhost',
                                       database='FGST',
                                       user='root',
                                       password='DATA')
        cursor = conn.cursor()
        cursor.execute('INSERT INTO GOOGLE(symbol,ltp,timeOfTrade) VALUES(%s,%s,%s)',(symbol,ltp,now))
        conn.commit()

        if conn.is_connected():
            print('Connected to MySQL database')

    except Error as e:
        print(e)

    finally:
        cursor.close()
        conn.close()


if __name__ == '__main__':
    connect()
    #quotes("GOOGL")
