import re
import os
# def replCompounds(field):
# 	compounds = retCompounds(field)
# 	for compound in compounds:
# 		elems, flag = extrElements(compound)
# 		print flag
# 		if flag:
# 			stdcomp = toStandard(elems)
# 			field = field.replace(compound, stdcomp)
# 	return field

#returns all the compounds in standard form in a list
#
global periodic_table 
periodic_table = []

def saveCompounds(field_exact):
	unwanted_char = ['{','}','_','$']
	field = field_exact
	for x in unwanted_char:
		field = field.replace(x,'');
	compounds = []
	compounds_2 = ""
	words_field = re.findall(r"[\w']+", field)
	for word in words_field:
		if(isCompound(word)):
			x,flag = extrElements(word)
			if flag :
				comp = toStandard(x)
				if not(comp in compounds):
					compounds.append(comp)
				# print("appended a compound")
		elif word in periodic_table:
			x,flag = extrElements(word)
			comp = toStandard(x)
			if not(comp in compounds):
				compounds.append(comp)
			# print("appended an element")
	for comp in compounds:
		if not(comp == ''):
			compounds_2 = compounds_2 + str(" ") + str(comp) + str(" ")
	return compounds_2


#converts set of elements and indices to a dictionary
def toStandard(elems):
	stdcomp = ""
	sortedKeys = sorted(elems)
	for key in sortedKeys:
		stdcomp = stdcomp + key + elems[key]
	return stdcomp

#extracts elements and their indices to a dictionary
def extrElements(compound):
	#print(compound)
	#print("\n \n")
	#print("extrElements")
	i = 0
	elems = {}
	max_i = len(compound)-1
	flag = True
	while i<max_i+1:
		temp_elem = ""
		temp_index = ""
		if ord(compound[i])>= 65 and ord(compound[i])<= 90:#caps
			if i<max_i:	
				if(ord(compound[i+1])>= 97 and ord(compound[i+1])<= 122 ):#small
					temp_elem = temp_elem + compound[i] + compound[i+1]
					if i<max_i-1:
						if (ord(compound[i+2])>= 48 and ord(compound[i+2])<= 57):#digit
							temp_index = compound[i+2]
							i = i+3
						else:
							temp_index = '1'
							i = i+2
					else:
						temp_index = '1'
						i = i+2
				elif ord(compound[i+1])>= 65 and ord(compound[i+1])<= 90:#caps
					temp_elem = compound[i]
					temp_index = '1'
					i = i + 1
				elif (ord(compound[i+1])>= 48 and ord(compound[i+1])<= 57 and i<max_i):#digit
					temp_elem = compound[i]
					temp_index = compound[i+1]
					i = i+2
				else:
					flag = False
					break
			else:
				temp_elem = compound[i]
				temp_index = '1'
				i = i+1
		else:
			break
		elems[temp_elem] = temp_index
		flag = flag and (temp_elem in periodic_table)
	return elems, flag


# def retCompounds(a):
# 	#returns all the compunds in a field
# 	compounds = []
# 	words_a = re.findall(r"[\w']+", a)
# 	for word in words_a:
# 		if(isCompound(word)):
# 			compounds.append(word)
# 		elif word in periodic_table:
# 			compounds.append(word)
# 	return compounds

def isCompound(x):
	#if more than one caps letter
	count = 0
	for char_x in x:
		if ord(char_x)>= 65 and ord(char_x)<= 90:
			count = count + 1;
		if count >= 2:
			break;
	return (count>=2)

def chemDetectInit():
	elements_file = open("elements.txt", "r") 
	for line in elements_file: 
		periodic_table.append(line.strip('\n'))


# x = 1;
# while 1==1:
# 	x = raw_input(">\n>enter the string which you want to standardise: ")
# 	if(x == "exit"):
# 		os.system('clear')
# 		print(">\n>\n>>Thank you! :) <<\n")
# 		break;
# 	print(saveCompounds(x))
