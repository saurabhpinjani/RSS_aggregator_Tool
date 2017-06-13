from elasticsearch import Elasticsearch
from nltk.tokenize import RegexpTokenizer
import nltk
from bs4 import BeautifulSoup
es = Elasticsearch(['http://localhost:9200'])

feed=es.get(index="rss_feed",doc_type="small",id=1)

#print "Title",feed['_source']['title']
#rint "Abstract",feed['_source']['content'][0]['value']
abstract= feed['_source']['content'][0]['value']
raw= abstract#.lower()
#print raw
#nltk.clean_html(raw)
#print raw
tokenizer = RegexpTokenizer(r'\w+')
soup = BeautifulSoup(raw, 'html.parser')
a= soup.get_text()
print a
#print soup.p
#tokens=tokenizer.tokenize(raw)
#print tokens