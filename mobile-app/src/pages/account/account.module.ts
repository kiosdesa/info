import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TranslateModule } from '@ngx-translate/core';
import { AccountPage } from './account';

@NgModule({
  declarations: [
    AccountPage,
  ],
  imports: [
    IonicPageModule.forChild(AccountPage),
    TranslateModule.forChild()
  ],
  exports: [
    AccountPage
  ]
})
export class AccountPageModule {}
