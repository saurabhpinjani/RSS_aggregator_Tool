import json

data = []
with open('/home/achyuth_koneti/Desktop/Musical_Instruments_5.json') as f:
    for line in f:
        data.append(json.loads(line))

f_10 = open("dataset/10.txt",'a')
f_20 = open("dataset/20.txt",'a')
f_30 = open("dataset/30.txt",'a')
f_40 = open("dataset/40.txt",'a')
f_50 = open("dataset/50.txt",'a')

for review in data:
	# f_name = "dataset/"+str(int(10*review['overall']))+'_'+str(review['reviewerID'])+'.txt'
	# f = open(fname,'w')
	text = str(review['reviewText']);
	text = text.replace('.',' ')
	text = text.replace(',',' ')
	text = text.replace('!',' ')
	text = text.replace('/',' ')
	text = text.replace('?',' ')
	text = text.replace('(',' ')
	text = text.replace(')',' ')
	text = text.replace("'",' ')
	text = text.replace('-',' ')
	text = text.replace(':',' ')
	text = text.replace(';',' ')
	text = text.replace('*',' ')
	text = text.replace(']',' ')
	text = text.replace('[',' ')
	text = text.lower()
	
	if review['overall']==1.0 :
		f_10.write(text+'\n')

	elif review['overall']==2.0 :
		f_20.write(text+'\n')

	elif review['overall']==3.0 :
		f_30.write(text+'\n')

	elif review['overall']==4.0 :
		f_40.write(text+'\n')

	elif review['overall']==5.0 :
		f_50.write(text+'\n')


f_10.close()
f_20.close()
f_30.close()
f_40.close()
f_50.close()