import gensim
from gensim.parsing import PorterStemmer
from gensim.models import Word2Vec
from gensim.models import Doc2Vec
from wikipedia import search, page
import sys
import numpy as np
from gensim.models.doc2vec import LabeledSentence
reload(sys)
sys.setdefaultencoding('utf8')
global_stemmer = PorterStemmer()

class LabeledLineSentence(object):
    def __init__(self, doc_list, labels_list):
       self.labels_list = labels_list
       self.doc_list = doc_list
    def __iter__(self):
        for idx, doc in enumerate(self.doc_list):
            yield LabeledSentence(words=doc.split(),tags=[self.labels_list[idx]])

class StemmingHelper(object):
    """
    Class to aid the stemming process - from word to stemmed form,
    and vice versa.
    The 'original' form of a stemmed word will be returned as the
    form in which its been used the most number of times in the text.
    """
 
    #This reverse lookup will remember the original forms of the stemmed
    #words
    word_lookup = {}
 
    @classmethod
    def stem(cls, word):
        """
        Stems a word and updates the reverse lookup.
        """
 
        #Stem the word
        stemmed = global_stemmer.stem(word)
        #Update the word lookup
        if stemmed not in cls.word_lookup:
            cls.word_lookup[stemmed] = {}
        cls.word_lookup[stemmed][word] = (
            cls.word_lookup[stemmed].get(word, 0) + 1)
 
        return stemmed
 
    @classmethod
    def original_form(cls, word):
        """
        Returns original form of a word given the stemmed version,
        as stored in the word lookup.
        """
        if word in cls.word_lookup:
            return max(cls.word_lookup[word].keys(),key=lambda x: cls.word_lookup[word][x])
        else:
            return word
# StemmingHelper.stem('learning')
# StemmingHelper.original_form('learn'


titles = search('machine learning')
wikipage = page(titles[0])        
content = (wikipage.content)
content = content.encode('utf-8')
content  = (content).split('\n')
sentences_uncut = []
for line in content:
	if(len(line)>0):
		temp_sentences = line.split('.')
		sentences_uncut.extend(temp_sentences)


sentences_cut = []
sentences_doc_cut = []
i=1
for sentence in sentences_uncut:
	sentence_label = "SENT_"+str(i)
	if(len(sentence)>0):
		sentence.replace(',','')
		sentence.replace(';','')
		sentence.replace('!','')
		sentence.replace(':','')
		sentence.replace('"','')
		sentence.replace('/','')
		sentence.replace('=','')
		temp_words = sentence.split(' ')
		words_modified = []
		while '' in temp_words:
			temp_words.remove('')
		for word in temp_words:
			word.replace('=','')
			if(len(word)>0):
				word = StemmingHelper.stem(word)
				words_modified.append(word)
		sentences_cut.append(words_modified)
		sentences_doc_cut.append(LabeledSentence(words=words_modified, tags=[sentence_label]))
	i=i+1


min_count = 2
size = 50
window = 4
model = Word2Vec(sentences_cut, min_count=min_count, size=size, window=window)
model.save('/home/achyuth_koneti/wiki_model')
model_doc = Doc2Vec(sentences_doc_cut)
model_doc.save('/home/achyuth_koneti/wiki_doc_model')