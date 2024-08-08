import webbrowser

# URL do site
url = "https://mediumblue-newt-815943.hostingersite.com/public/client/menu.php"

# Abre 10 abas no navegador
for _ in range(5):
    webbrowser.open_new_tab(url)
    
