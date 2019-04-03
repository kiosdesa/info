import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { ConfirmPayPage } from './confirm-pay';

@NgModule({
  declarations: [
    ConfirmPayPage,
  ],
  imports: [
    IonicPageModule.forChild(ConfirmPayPage),
    TranslateModule.forChild()
  ],
  exports: [
    ConfirmPayPage
  ]
})
export class ConfirmPayPageModule { }
