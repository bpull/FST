from nytimesarticle import articleAPI
import math
import sys
import re
from datetime import datetime
import pprint
import pysentiment as ps
from googlefinance import getQuotes,getNews
from newspaper import Article
'''
Takes as input the output from tradeCalendar2.py, the trading days for a given month
Takes up to 10 NYT business and 10 Reuters business articles from the given dates
Using their lead paragraphs, analyzes the words used in the headlines to determine if general market sentiment is positive or negative. 
Finally, outputs the ratio of words identified as positive in a general sentiment dictionary and a financial sentiment dictionary for each trading day. 
'''



def parse_articles(articles):
    '''
    This function takes in a response to the NYT api and parses
    the articles into a list of dictionaries
    '''
    news = []
    for i in articles['response']['docs']:
        dic = {}
        dic['id'] = i['_id']
        if i['abstract'] is not None:
            dic['abstract'] = i['abstract'].encode("utf8")
        dic['headline'] = i['headline']['main'].encode("utf8")
        dic['desk'] = i['news_desk']
        if i['lead_paragraph'] is not None:
            dic['lead_paragraph']=i['lead_paragraph'].encode("utf8")
        elif i['abstract'] is not None:
            dic['lead_paragraph'] = i['abstract'].encode("utf8")
        elif i['snippet'] is not None:
            dic['lead_paragraph'] = i['snippet'].encode("utf8")
        else:
            dic['lead_paragraph'] = i['headline']['main'].encode("utf8")
        dic['date'] = i['pub_date'][0:10] # cutting time of day.
        dic['section'] = i['section_name']
        if i['snippet'] is not None:
            dic['snippet'] = i['snippet'].encode("utf8")
        dic['source'] = i['source']
        dic['type'] = i['type_of_material']
        dic['url'] = i['web_url']
        dic['word_count'] = i['word_count']
        # locations
        locations = []
        for x in range(0,len(i['keywords'])):
            if 'glocations' in i['keywords'][x]['name']:
                locations.append(i['keywords'][x]['value'])
        dic['locations'] = locations
        # subject
        subjects = []
        for x in range(0,len(i['keywords'])):
            if 'subject' in i['keywords'][x]['name']:
                subjects.append(i['keywords'][x]['value'])
        dic['subjects'] = subjects   
        news.append(dic)
    return(news) 

def search(startDate,endDate):
    api = articleAPI('d38bd70f341f41c3acf36992e594447a')
    nytarticles = api.search(fq = {'source':'The New York Times','news_desk':'Business'}, 
     begin_date = startDate,end_date=endDate )
    reutersarticles = api.search(fq = {'source':'Reuters','section_name':'Business'}, 
                                 begin_date = startDate,end_date=endDate)
    nytart = parse_articles(nytarticles)
    reutersart = parse_articles(reutersarticles)
    #print art
    pp = pprint.PrettyPrinter(indent=4)
    #pp.pprint(reutersart)
    
    iv4 = ps.HIV4()
    lm = ps.LM()
    numPosIV=0
    numNegIV=0
    numPosLM=0
    numNegLM=0
    for art in nytart:
        text = art['lead_paragraph']
        punc = ['.',',','?','!','\n',':',';','\"','\'']
        for item in punc:
            if item == '\n':
                text = text.replace(item,' ')
            else:
                text = text.replace(item,'')
        ivtokens = iv4.tokenize(text)
        ivscore = iv4.get_score(ivtokens)
        tokens = lm.tokenize(text)
        score = lm.get_score(tokens)
        numPosIV=numPosIV+ivscore['Positive']
        numNegIV=numNegIV+ivscore['Negative']
        numPosLM=numPosLM+score['Positive']
        numNegLM=numNegLM+score['Negative']
    
    for art in reutersart:
        text = art['lead_paragraph']
        punc = ['.',',','?','!','\n',':',';','\"','\'']
        for item in punc:
            if item == '\n':
                text = text.replace(item,' ')
            else:
                text = text.replace(item,'')
        ivtokens = iv4.tokenize(text)
        ivscore = iv4.get_score(ivtokens)
        tokens = lm.tokenize(text)
        score = lm.get_score(tokens)
        numPosIV=numPosIV+ivscore['Positive']
        numNegIV=numNegIV+ivscore['Negative']
        numPosLM=numPosLM+score['Positive']
        numNegLM=numNegLM+score['Negative']


    results = []
    results.append(float(numPosIV)/(numPosIV+numNegIV))
    results.append(float(numPosLM)/(numPosLM+numNegLM))
    return(results)

with open(sys.argv[1],'r') as f:
    dates = f.read()
dates = re.sub('[[\]\']','',dates)
dates = [x.strip() for x in dates.split(',')]
startDate = re.sub('-','',dates.pop(0))
res = []
for date in dates:
    endDate = re.sub('-','',date)
    res.append(search(startDate,endDate))
    startDate = endDate

print res

