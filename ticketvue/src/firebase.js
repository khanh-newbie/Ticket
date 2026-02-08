import { initializeApp } from "firebase/app";
import { getAuth, GoogleAuthProvider, FacebookAuthProvider } from "firebase/auth";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyCsN3jTyNTazotiTEqEwAnE4KSHMkqywOk",
  authDomain: "ticket-78dd4.firebaseapp.com",
  projectId: "ticket-78dd4",
  storageBucket: "ticket-78dd4.firebasestorage.app",
  messagingSenderId: "98341836231",
  appId: "1:98341836231:web:4667471604c5aeb2a035e2",
  measurementId: "G-71FEKY8FV2"
};

// 🔥 Khởi tạo Firebase App
const app = initializeApp(firebaseConfig);

// ✅ Khởi tạo Auth
const auth = getAuth(app);

// ✅ Các provider đăng nhập
const googleProvider = new GoogleAuthProvider();
const facebookProvider = new FacebookAuthProvider();

// ✅ Export để dùng ở nơi khác
export { auth, googleProvider, facebookProvider };