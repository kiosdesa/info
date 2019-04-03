import { NgModule } from '@angular/core';
import { SeparateNumberPipe } from '../pipe/separate-number';
import { TitleCasePipe } from '../pipe/title-case';

@NgModule({
  imports: [
    // dep modules
  ],
  declarations: [ 
    SeparateNumberPipe,
    TitleCasePipe
  ],
  exports: [
    SeparateNumberPipe,
    TitleCasePipe
  ]
})
export class OfanCoreShareModule {}