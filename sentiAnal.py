"""
A simple script that uses the LM dictionary which specifically contains negative and positive sentiment terms. Input to the dictionary is in the form of a string. The package scores the input text and returns a polarity index which is how negative or positive the text is in financial terms as well as total negative and positive terms and a subjectivity index about how relevant the text is
"""
import pysentiment as ps
from googlefinance import getQuotes,getNews
from newspaper import Article


news = getNews('GOOGL')
lm = ps.LM()
count = 0

for item in news:
    article = Article(item['u'])
    article.download()
    try:
        article.parse()
    except:
        print 'Parsing Failed!'
    text = article.text
    punc = ['.',',','?','!','\n',':',';']
    for item in punc:
        if item == '\n':
            text = text.replace(item,' ')
        else:
            text = text.replace(item,'')
    tokens = lm.tokenize(text)
    score = lm.get_score(tokens)
    print article.title, score
    count = count + 1
    if count == 10:
        break


#stoplist = set('for a of the and to in inc'.split())
#for word in stoplist:
#    text = text.replace(word,'')


