<template>
  <div class="justify-content-start">
      <div class="col-12">
          <!-- show messages -->
          <div class="row mb-2">
            <div v-for="message in messages" v-bind:key="message.id">
              <div :class="message.user_type == 'bot' ? 'col-6 align-self-start': 'col-6 align-self-end'">
                <div :class="message.user_type == 'bot' ? 'alert alert-info': 'alert alert-warning'">
                  <label> 
                    {{ message.sender_name ? message.sender_name : "You" }} : {{ message.message }}
                  </label>
                </div>
              </div>
            </div>
            <br />
          </div>

          <form class="form" @submit.prevent="sendMessage">
            <div class="mb-3">
              <input type="text"
                id="chat"
                class="form-control"
                placeholder="Your message..."
                required
                v-model="newMessage"
                @keyup.enter="sendMessage">
            </div>
            <div class="mb-3">    
              <button
                type="submit"
                class="btn btn-primary">
                Send message
              </button>
            </div>
          </form>

          <button type="button" class="btn btn-danger" @click="logout">Logout</button>
      </div>
    </div>
</template>
<script setup>
    import { reactive, onMounted, watch, ref } from "vue";
    import API from "../services/API";
    import TokenService from "../services/TokenService";
     
    const messages = ref([]);
    const newMessage = ref("");
    const messageEnded = ref(false);
    
    onMounted(() => {
      console.log("chat started");
      initMessage();
    });
    
    const initMessage = async () => {
      try {
        const response = await API.post("/chat/init");
    
        console.group("init message");
        console.log(response.data);
        console.groupEnd();
    
        fetchMessage(response.data.messages);
        newMessage.value = "";
      } catch (e) {
        console.error(e);
      }
    };
    
    const addMessage = async (newMessageFromSendMessage) => {
      try {
        const response = await API.post("/chat/send", {
          message: newMessageFromSendMessage,
        });
    
        console.log(response.data);
    
        if (response.data.dialogState == "ReadyForFulfillment") {
          messageEnded.value = true;
          location.clear();
        }
    
        fetchMessage(response.data.messages);
        newMessage.value = ""
    
      } catch (e) {
        console.error(e);
      }
    };
    
    const sendMessage = () => {
      if (newMessage.value.trim()) {
        console.log("new message ref trim");
        addMessage(newMessage.value.trim());
      }
    };
    
    const fetchMessage = (incomingMessages) => {
      if (incomingMessages) {
        console.group("fetch messages");
        console.log(incomingMessages);
        console.groupEnd();
    
        incomingMessages.forEach((element) => {
          messages.value.push(element);
        });
      }
    };

    const logout = async () => {
      console.log("logout now");
      try {
        const response = await API.get("logout");
        console.log(response.data);
        TokenService.clearToken()
        location.reload();
      } catch (e) {
        console.error(e);
      }
    };
</script>