from bs4 import BeautifulSoup
import requests
import feedparser
import urllib
r = requests.get('http://pubs.acs.org/doi/abs/10.1021/acsami.7b02398')
data=r.text
soup = BeautifulSoup(data, 'html.parser')
#print soup.get_text()
#soup.find_all("head")
#soup.find_all("title")
abstract_tags=["dc.Description","citation_abstract"]

meta_list=soup.find_all('meta')
for item in meta_list:
	if(item.has_attr('name')):
		if(item['name'] in abstract_tags):
			print item['content']

#print(soup.prettify())
meta_list=soup.find_all('h2',id='abstractBox')
print meta_list

feed= feedparser.parse(soup.get_text())
print feed

with urllib.request.urlopen('http://pubs.acs.org/doi/abs/10.1021/acsami.7b02398') as url:
      response = url.read()
print feedparser.parse(response)      