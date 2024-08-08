import subprocess
import time

# URL do site
url = "https://mediumblue-newt-815943.hostingersite.com/public/client/menu.php"

# Abre 10 abas no navegador padrão
for _ in range(10):
    subprocess.run(["start", "chrome", url], shell=True)
    time.sleep(1)  # Pausa para garantir que a aba seja aberta

    #Windows: Use start com o argumento shell=True e especifique o navegador, se necessário.