# gensim modules
from gensim import utils
from gensim.models.doc2vec import LabeledSentence
from gensim.models import Doc2Vec

# numpy
import numpy

# random
from random import shuffle

# classifier
from sklearn.linear_model import LogisticRegression

#k-means
from sklearn.cluster import KMeans

#pyplot
import matplotlib.pyplot as plt


class LabeledLineSentence(object):
    def __init__(self, sources):
        self.sources = sources
        
        flipped = {}
        
        # make sure that keys are unique
        for key, value in sources.items():
            if value not in flipped:
                flipped[value] = [key]
            else:
                raise Exception('Non-unique prefix encountered')
    
    def __iter__(self):
        for source, prefix in self.sources.items():
            with utils.smart_open(source) as fin:
                for item_no, line in enumerate(fin):
                    yield LabeledSentence(utils.to_unicode(line).split(), [prefix + '_%s' % item_no])
    
    def to_array(self):
        self.sentences = []
        for source, prefix in self.sources.items():
            with utils.smart_open(source) as fin:
                for item_no, line in enumerate(fin):
                    self.sentences.append(LabeledSentence(utils.to_unicode(line).split(), [prefix + '_%s' % item_no]))
        return self.sentences
    
    def sentences_perm(self):
        shuffle(self.sentences)
        return self.sentences

sources = { '10.txt':'train_10',
			'20.txt':'train_20',
			'30.txt':'train_30',
			'40.txt':'train_40',
			'50.txt':'train_50'}

sentences = LabeledLineSentence(sources)
model = Doc2Vec(min_count=1, window=10, size=100, sample=1e-4, negative=5, workers=8)
model.build_vocab(sentences.to_array())

model.train(sentences.sentences_perm(),total_examples=model.corpus_count,epochs=10)
model.save('Reviews_model_equal.d2v')

#use model.docvecs[#label] to get the vector for that document
train_arrays = numpy.zeros((1000, 100))
train_labels = numpy.zeros((1000))

for i in range(200):
    prefix_train_10 = 'train_10_' + str(i)
    prefix_train_20 = 'train_20_' + str(i)
    prefix_train_30 = 'train_30_' + str(i)
    prefix_train_40 = 'train_40_' + str(i)
    prefix_train_50 = 'train_50_' + str(i)
    
    
    train_arrays[i] = model.docvecs[prefix_train_10]
    train_arrays[200 + i] = model.docvecs[prefix_train_20]
    train_arrays[400 + i] = model.docvecs[prefix_train_30]
    train_arrays[600 + i] = model.docvecs[prefix_train_40]
    train_arrays[800 + i] = model.docvecs[prefix_train_50]
    
    train_labels[i] = 1
    train_labels[200 + i] = 1
    train_labels[400 + i] = 1
    train_labels[600 + i] = 0
    train_labels[800 + i] = 0




classifier_0 = LogisticRegression()
classifier_0.fit(train_arrays, train_labels)
x_10_1 = numpy.zeros((217,100))
x_20_1 = numpy.zeros((217,100))
x_30_1 = numpy.zeros((217,100))
x_40_1 = numpy.zeros((217,100))
x_50_1 = numpy.zeros((217,100))


i = 0
while True:
    try:
        x_10_1[i] = model.docvecs['train_10_' + str(i)]
        x_20_1[i] = model.docvecs['train_20_' + str(i)]
        x_30_1[i] = model.docvecs['train_30_' + str(i)]
        x_40_1[i] = model.docvecs['train_40_' + str(i)]
        x_50_1[i] = model.docvecs['train_50_' + str(i)]
        # x_all[i] = x_10[i]
        i = i+1
    except KeyError:
        break;


# i = 0
# while True:
#     try:
#         x_20_1[i] = model_1.docvecs['train_20_' + str(i)]
#         # x_all[i+217] = x_20[i]
#         i = i+1
#     except KeyError:
#         break;


# i = 0
# while True:
#     try:
#         x_30_1[i] = model_1.docvecs['train_30_' + str(i)]
#         # x_all[i+217+250] = x_30[i]
#         i = i+1
#     except KeyError:
#         break;


# i = 0
# while True:
#     try:
#         x_40_1[i] = model.docvecs['train_40_' + str(i)]
#         # x_all[i+217+250+772] = x_40[i]
#         i = i+1
#     except KeyError:
#         break;


# i = 0
# while True:
#     try:
#         x_50_1[i] = model_1.docvecs['train_50_' + str(i)]
#         # x_all[i+217+250+772+2084] = x_50[i]
#         i = i+1
#     except KeyError:
#         break;

x_10_t = x_10.transpose()
x_20_t = x_20.transpose()
x_30_t = x_30.transpose()
x_40_t = x_40.transpose()
x_50_t = x_50.transpose()

Data = np.matrix(zip(x_10_t,x_20_t,x_30_t,x_40_t,x_50_t))
kmeans = KMeans(n_clusters=5).fit(X)