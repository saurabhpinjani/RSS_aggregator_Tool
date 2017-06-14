from bs4 import BeautifulSoup
import requests

r = requests.get('http://pubs.acs.org/doi/abs/10.1021/acsami.7b02398')
data=r.text
soup = BeautifulSoup(data, 'html.parser')
#soup.find_all("head")
#soup.find_all("title")
abstract_tags=["dc.Description","citation_abstract"]

meta_list=soup.find_all('meta')
for item in meta_list:
	if(item.has_attr('name')):
		if(item['name'] in abstract_tags):
			print item['content']

#print(soup.prettify())