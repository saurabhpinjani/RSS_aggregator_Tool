from elasticsearch import Elasticsearch
from nltk.tokenize import RegexpTokenizer
import nltk
from bs4 import BeautifulSoup
from stop_words import get_stop_words
from nltk.stem.porter import PorterStemmer
from nltk.stem.snowball import SnowballStemmer
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
#print a
tokens = tokenizer.tokenize(a)

#print tokens
en_stop = get_stop_words('en')
stopped_tokens = [i for i in tokens if not i in en_stop]
print stopped_tokens
snowball_stemmer = SnowballStemmer("english")
snowball_stemmed_tokens =[snowball_stemmer.stem(token) for token in stopped_tokens]
print snowball_stemmed_tokens

porter_stemmer = PorterStemmer()
porter_stemmed_tokens =[porter_stemmer.stem(token) for token in stopped_tokens]
print porter_stemmed_tokens

diff =[i for i in porter_stemmed_tokens if i not in snowball_stemmed_tokens]

print "Diff----------------------------------------------------------------------------"
print diff

diff =[i for i in snowball_stemmed_tokens if i not in porter_stemmed_tokens]

print "Diff----------------------------------------------------------------------------"
print diff
#print soup.p
#tokens=tokenizer.tokenize(raw)
#print tokens
