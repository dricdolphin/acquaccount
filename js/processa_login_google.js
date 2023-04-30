function cancela_autologin() {
    let url = new URL(window.location.href);
    let logout = url.searchParams.get("logout");

    if (logout == '1') {
        let button = document.getElementById("signout_button");
        button.onclick = () => {
          google.accounts.id.disableAutoSelect();
        }
    }
}