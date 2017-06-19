from elasticsearch import Elasticsearch
from nltk.tokenize import RegexpTokenizer
import nltk
from bs4 import BeautifulSoup
from stop_words import get_stop_words
from nltk.stem.snowball import SnowballStemmer
import os
from gensim import corpora, models
import gensim
import rss_url_read

es = Elasticsearch(['http://localhost:9200'])

tokenizer = RegexpTokenizer(r'\w+')
snowball_stemmer = SnowballStemmer("english")
#feed=es.get(index="rss_feed",doc_type="journal of materials chemistry c",id=1)
docs= rss_url_read.read_feed()
texts=[]
for doc in docs:
#	if('content' in doc.keys()):
#		abstract = doc['content']
#	else:
#		abstract = doc['summary']

	raw= doc['title']
	print raw

	soup = BeautifulSoup(raw, 'html.parser')
	raw_without_tags= soup.get_text()


	tokens = tokenizer.tokenize(raw_without_tags)


	en_stop = get_stop_words('en')
	stopped_tokens = [i for i in tokens if not i in en_stop]
	print stopped_tokens

	
	snowball_stemmed_tokens =[snowball_stemmer.stem(token) for token in stopped_tokens]
	print snowball_stemmed_tokens
	texts.append(snowball_stemmed_tokens)


# turn our tokenized documents into a id <-> term dictionary
dictionary = corpora.Dictionary(texts)
    
# convert tokenized documents into a document-term matrix
corpus = [dictionary.doc2bow(text) for text in texts]

# generate LDA model
ldamodel = gensim.models.ldamodel.LdaModel(corpus,num_topics=20, id2word = dictionary,chunksize=200,passes=20)
ldamodel.print_topics(20)
ldamodel.save(os.getcwd()+'/LDA/lda_model_file')

