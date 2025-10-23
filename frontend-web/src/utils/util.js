const uuidv4 = ()=>{
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = crypto.getRandomValues(new Uint8Array(1))[0] & 15;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}
const getCookie = (name)=>{
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}
const  setCookie = (name, value, days=365) =>{
    const expires = new Date(Date.now() + days*2*86400*1000).toUTCString();
    document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; expires=' + expires + '; SameSite=Lax';
}
export default {getCookie, setCookie, uuidv4}