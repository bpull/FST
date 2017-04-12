for i in range(100):
    topy = str(i)+"make.py"
    fp2 = open(topy,"w")
    fp = open("mickyyear.py","r")
    fp2.write("import subprocess,os\n")
    filename = "justtick"+str(i)+".txt"
    fp2.write('fp = open("'+filename+'","r")\n')
    for line in fp:
        fp2.write(line)
    fp.close()
    fp2.close()
