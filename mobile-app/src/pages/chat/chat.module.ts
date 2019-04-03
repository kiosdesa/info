import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { ChatPage } from './chat';

@NgModule({
  declarations: [
    ChatPage,
  ],
  imports: [
    IonicPageModule.forChild(ChatPage),
    TranslateModule.forChild()
  ],
  exports: [
    ChatPage
  ]
})
export class ChatPageModule { }
