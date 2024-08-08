import subprocess
import time

# URL do site
url = "https://mediumblue-newt-815943.hostingersite.com/public/client/menu.php"

# Abre 10 abas no navegador padrão
for _ in range(10):
    subprocess.run(["xdg-open", url])
    time.sleep(1)  # Pausa para garantir que a aba seja aberta

    #Linux: Use xdg-open.