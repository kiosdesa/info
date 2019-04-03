import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TranslateModule } from '@ngx-translate/core';
import { ConfirmotpPage } from './confirmotp';

@NgModule({
  declarations: [
    ConfirmotpPage,
  ],
  imports: [
    IonicPageModule.forChild(ConfirmotpPage),
    TranslateModule.forChild()
  ],
  exports: [
    ConfirmotpPage
  ]
})
export class ConfirmotpPageModule {}
