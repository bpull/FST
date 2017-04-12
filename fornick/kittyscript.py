fp2 = open("companies.txt","r")
fp3 = open("final.txt","w")

for line in fp2:
    fp1 = open("fornick.txt","r")
    for data in fp1:
        if data.strip().split()[0] == line.split()[0]:
            fp3.write(" ".join(line.split()[1:])+"," +",".join(data.strip().split())+"\n")
    fp1.close()
