import os,shutil
fp = open("justtick.txt")
for line in fp:
    shutil.rmtree(line.strip(),ignore_errors=True)
    if not os.path.exists(line.strip()):
        os.makedirs(line.strip())
