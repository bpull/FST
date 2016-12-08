import mysql.connector
from mysql.connector import Error


def connect():
    """ Connect to MySQL database """
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
    try:
        conn = mysql.connector.connect(host='localhost',
                                       database='FGST',
                                       user='root',
                                       password='DATA')
        cursor = conn.cursor()
        for stock in symbols:
            query = "INSERT INTO stockSymbols(symbol) VALUES(\'"+stock+"\')"
            print(query)
            cursor.execute('INSERT INTO stockSymbols(symbol) VALUES(%s)',(stock,))
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
