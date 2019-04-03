import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TranslateModule } from '@ngx-translate/core';
import { ChangepasswordPage } from './changepassword';

@NgModule({
  declarations: [
    ChangepasswordPage,
  ],
  imports: [
    IonicPageModule.forChild(ChangepasswordPage),
    TranslateModule.forChild()
  ],
  exports: [
    ChangepasswordPage
  ]
})
export class ChangepasswordPageModule {}
